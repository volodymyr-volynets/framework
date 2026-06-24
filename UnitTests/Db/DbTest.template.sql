SELECT ? AS a, ? AS b, ? AS c, <% escape|quote $x1 . '-' . $x2 . '-' . $x3 %> as d
WHERE '1' in(<% in $x4 %>)